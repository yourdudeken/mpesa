package types

type Span interface {
	SetAttribute(key string, value interface{})
	AddEvent(name string, attributes map[string]interface{})
	End()
}

type noopSpan struct{}

func (n *noopSpan) SetAttribute(_ string, _ interface{})        {}
func (n *noopSpan) AddEvent(_ string, _ map[string]interface{}) {}
func (n *noopSpan) End()                                        {}

type Tracer interface {
	StartSpan(name string, attributes map[string]interface{}) Span
}

type NoopTracer struct{}

func (n *NoopTracer) StartSpan(_ string, _ map[string]interface{}) Span {
	return &noopSpan{}
}
