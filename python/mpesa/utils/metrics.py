from typing import Any, Optional


class MetricsCollector:
    def increment(self, metric: str, tags: Optional[dict[str, str]] = None, value: int = 1) -> None:
        pass

    def gauge(self, metric: str, value: float, tags: Optional[dict[str, str]] = None) -> None:
        pass

    def timing(self, metric: str, duration_ms: float, tags: Optional[dict[str, str]] = None) -> None:
        pass

    def histogram(self, metric: str, value: float, tags: Optional[dict[str, str]] = None) -> None:
        pass


class NoopMetricsCollector(MetricsCollector):
    pass
