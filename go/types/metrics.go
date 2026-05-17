package types

type MetricsCollector interface {
	Increment(metric string, tags map[string]string, value int)
	Gauge(metric string, value float64, tags map[string]string)
	Timing(metric string, durationMs float64, tags map[string]string)
	Histogram(metric string, value float64, tags map[string]string)
}

type NoopMetricsCollector struct{}

func (n *NoopMetricsCollector) Increment(_ string, _ map[string]string, _ int)     {}
func (n *NoopMetricsCollector) Gauge(_ string, _ float64, _ map[string]string)     {}
func (n *NoopMetricsCollector) Timing(_ string, _ float64, _ map[string]string)    {}
func (n *NoopMetricsCollector) Histogram(_ string, _ float64, _ map[string]string) {}
