package types

import (
	"errors"
	"sync"
	"time"
)

var ErrCircuitBreakerOpen = errors.New("circuit breaker is open")

type CircuitState string

const (
	CircuitClosed   CircuitState = "closed"
	CircuitOpen     CircuitState = "open"
	CircuitHalfOpen CircuitState = "half_open"
)

type CircuitBreakerConfig struct {
	FailureThreshold int
	SuccessThreshold int
	TimeoutMs        int
}

type CircuitBreaker struct {
	mu               sync.Mutex
	failureThreshold int
	successThreshold int
	timeout          time.Duration
	state            CircuitState
	failureCount     int
	successCount     int
	lastFailureTime  time.Time
}

func NewCircuitBreaker(config CircuitBreakerConfig) *CircuitBreaker {
	if config.FailureThreshold == 0 {
		config.FailureThreshold = 5
	}
	if config.SuccessThreshold == 0 {
		config.SuccessThreshold = 2
	}
	if config.TimeoutMs == 0 {
		config.TimeoutMs = 30000
	}
	return &CircuitBreaker{
		failureThreshold: config.FailureThreshold,
		successThreshold: config.SuccessThreshold,
		timeout:          time.Duration(config.TimeoutMs) * time.Millisecond,
		state:            CircuitClosed,
	}
}

func (cb *CircuitBreaker) State() CircuitState {
	cb.mu.Lock()
	defer cb.mu.Unlock()
	if cb.state == CircuitOpen && time.Since(cb.lastFailureTime) >= cb.timeout {
		cb.state = CircuitHalfOpen
		cb.successCount = 0
	}
	return cb.state
}

func (cb *CircuitBreaker) Execute(fn func() error) error {
	if cb.State() == CircuitOpen {
		return ErrCircuitBreakerOpen
	}

	err := fn()

	cb.mu.Lock()
	defer cb.mu.Unlock()

	if err != nil {
		cb.failureCount++
		cb.lastFailureTime = time.Now()
		if cb.failureCount >= cb.failureThreshold {
			cb.state = CircuitOpen
		}
	} else {
		if cb.state == CircuitHalfOpen {
			cb.successCount++
			if cb.successCount >= cb.successThreshold {
				cb.state = CircuitClosed
				cb.failureCount = 0
				cb.successCount = 0
			}
		} else {
			cb.failureCount = 0
		}
	}

	return err
}

func (cb *CircuitBreaker) Reset() {
	cb.mu.Lock()
	defer cb.mu.Unlock()
	cb.state = CircuitClosed
	cb.failureCount = 0
	cb.successCount = 0
}

type RateLimiterConfig struct {
	TokensPerSecond float64
	BurstSize       int
}

type TokenBucketRateLimiter struct {
	mu              sync.Mutex
	tokensPerSecond float64
	burstSize       int
	tokens          float64
	lastRefill      time.Time
}

func NewTokenBucketRateLimiter(config RateLimiterConfig) *TokenBucketRateLimiter {
	if config.TokensPerSecond == 0 {
		config.TokensPerSecond = 5
	}
	if config.BurstSize == 0 {
		config.BurstSize = 10
	}
	return &TokenBucketRateLimiter{
		tokensPerSecond: config.TokensPerSecond,
		burstSize:       config.BurstSize,
		tokens:          float64(config.BurstSize),
		lastRefill:      time.Now(),
	}
}

func (rl *TokenBucketRateLimiter) Acquire() {
	for !rl.TryAcquire() {
		time.Sleep(10 * time.Millisecond)
	}
}

func (rl *TokenBucketRateLimiter) TryAcquire() bool {
	rl.mu.Lock()
	defer rl.mu.Unlock()

	now := time.Now()
	elapsed := now.Sub(rl.lastRefill).Seconds()
	rl.tokens = float64(rl.burstSize)
	if rl.tokens+elapsed*rl.tokensPerSecond < float64(rl.burstSize) {
		rl.tokens += elapsed * rl.tokensPerSecond
	}
	rl.lastRefill = now

	if rl.tokens >= 1.0 {
		rl.tokens -= 1.0
		return true
	}
	return false
}

type NoopRateLimiter struct{}

func (n *NoopRateLimiter) Acquire()         {}
func (n *NoopRateLimiter) TryAcquire() bool { return true }
