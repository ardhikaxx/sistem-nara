import json
import os
import sys
import warnings
from contextlib import contextmanager
from pathlib import Path
from typing import Optional


BASE_DIR = Path(__file__).resolve().parent
OSS_DIR = BASE_DIR / "oss-analisis"


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
    oss_dir = OSS_DIR
    if not oss_dir.exists():
        sys.stderr.write("Folder model tidak ditemukan di scripts/oss-analisis.\n")
        return 1

    # Use local NLTK data dir to avoid external downloads
    nltk_data_dir = oss_dir / "nltk_data"
    nltk_data_dir.mkdir(parents=True, exist_ok=True)
    os.environ.setdefault("NLTK_DATA", str(nltk_data_dir))

    required_files = [
        oss_dir / "config.py",
        oss_dir / "src" / "predict.py",
        oss_dir / "src" / "preprocess.py",
        oss_dir / "src" / "naive_bayes.py",
        oss_dir / "models" / "naive_bayes_model.pkl",
        oss_dir / "models" / "vectorizer.pkl",
        oss_dir / "models" / "label_encoder.pkl",
    ]
    missing = [str(path) for path in required_files if not path.exists()]
    if missing:
        sys.stderr.write("File model tidak lengkap:\n" + "\n".join(missing) + "\n")
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
