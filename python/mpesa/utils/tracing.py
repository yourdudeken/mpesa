from typing import Any, Callable, Optional


class Span:
    def set_attribute(self, key: str, value: Any) -> None:
        pass

    def add_event(self, name: str, attributes: Optional[dict[str, Any]] = None) -> None:
        pass

    def end(self) -> None:
        pass


class Tracer:
    def start_span(self, name: str, attributes: Optional[dict[str, Any]] = None) -> Span:
        return Span()

    def with_span(self, name: str, attributes: Optional[dict[str, Any]] = None) -> "SpanContext":
        return SpanContext(self, name, attributes)


class SpanContext:
    def __init__(self, tracer: Tracer, name: str, attributes: Optional[dict[str, Any]] = None) -> None:
        self._tracer = tracer
        self._name = name
        self._attributes = attributes

    def __enter__(self) -> Span:
        self._span = self._tracer.start_span(self._name, self._attributes)
        return self._span

    def __exit__(self, *args: Any) -> None:
        self._span.end()


class NoopTracer(Tracer):
    pass


def with_span(tracer: Tracer, name: str, attributes: Optional[dict[str, Any]] = None) -> SpanContext:
    return tracer.with_span(name, attributes)
