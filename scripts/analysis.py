import json
import os
import sys
import warnings
from contextlib import contextmanager
from pathlib import Path
from typing import Optional


DEFAULT_OSS_DIR = r"D:\oss-analisis"


@contextmanager
def suppress_output():
    with open(os.devnull, "w") as devnull:
        old_stdout = sys.stdout
        old_stderr = sys.stderr
        try:
            sys.stdout = devnull
            sys.stderr = devnull
            yield
        finally:
            sys.stdout = old_stdout
            sys.stderr = old_stderr


def load_accuracy(oss_dir: Path) -> Optional[float]:
    results_path = oss_dir / "results" / "evaluation_results.json"
    if not results_path.exists():
        return None
    try:
        data = json.loads(results_path.read_text(encoding="utf-8"))
        return float(data.get("metrics", {}).get("accuracy"))
    except Exception:
        return None


def main() -> int:
    oss_dir = Path(os.environ.get("OSS_ANALYSIS_DIR", DEFAULT_OSS_DIR))
    if not oss_dir.exists():
        sys.stderr.write("OSS_ANALYSIS_DIR tidak ditemukan.\n")
        return 1

    sys.path.insert(0, str(oss_dir))
    warnings.filterwarnings("ignore")

    try:
        from src.predict import SentimentPredictor
    except Exception as exc:
        sys.stderr.write(f"Gagal memuat modul predict: {exc}\n")
        return 1

    raw = sys.stdin.read()
    if not raw.strip():
        sys.stderr.write("Input kosong.\n")
        return 1

    try:
        payload = json.loads(raw)
    except json.JSONDecodeError:
        sys.stderr.write("Input bukan JSON valid.\n")
        return 1

    items = payload.get("items", [])
    if not items:
        output = {"results": [], "model_accuracy": load_accuracy(oss_dir)}
        print(json.dumps(output))
        return 0

    texts = [item.get("text", "") for item in items]

    with suppress_output():
        predictor = SentimentPredictor()
        results = predictor.predict_batch(texts)

    output_results = []
    for item, result in zip(items, results):
        output_results.append({
            "id": item.get("id"),
            "sentiment": result.get("sentiment"),
            "confidence": result.get("confidence"),
        })

    output = {
        "results": output_results,
        "model_accuracy": load_accuracy(oss_dir),
    }

    print(json.dumps(output, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
