import time
import threading
from typing import Optional


class TokenBucketRateLimiter:
    def __init__(self, tokens_per_second: float = 5.0, burst_size: int = 10) -> None:
        self._tokens_per_second = tokens_per_second
        self._burst_size = burst_size
        self._tokens = float(burst_size)
        self._last_refill = time.time()
        self._lock = threading.Lock()

    def acquire(self) -> None:
        while not self._try_acquire():
            time.sleep(0.01)

    def try_acquire(self) -> bool:
        with self._lock:
            now = time.time()
            elapsed = now - self._last_refill
            self._tokens = min(self._burst_size, self._tokens + elapsed * self._tokens_per_second)
            self._last_refill = now

            if self._tokens >= 1.0:
                self._tokens -= 1.0
                return True
            return False

    def _try_acquire(self) -> bool:
        with self._lock:
            now = time.time()
            elapsed = now - self._last_refill
            self._tokens = min(self._burst_size, self._tokens + elapsed * self._tokens_per_second)
            self._last_refill = now

            if self._tokens >= 1.0:
                self._tokens -= 1.0
                return True
            return False


class NoopRateLimiter:
    def acquire(self) -> None:
        pass

    def try_acquire(self) -> bool:
        return True


class RateLimiterConfig:
    def __init__(self, tokens_per_second: float = 5.0, burst_size: int = 10) -> None:
        self.tokens_per_second = tokens_per_second
        self.burst_size = burst_size
