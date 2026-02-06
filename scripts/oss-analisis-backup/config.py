import sys
import os
from pathlib import Path

# Path dasar
BASE_DIR = Path(__file__).parent

# Path data
DATA_DIR = BASE_DIR / "data"
RAW_DATA_PATH = DATA_DIR / "reviews.csv"
PROCESSED_DATA_PATH = DATA_DIR / "processed_reviews.csv"
SPLIT_DATA_DIR = DATA_DIR / "split_data"

# Path model
MODEL_DIR = BASE_DIR / "models"
MODEL_PATH = MODEL_DIR / "naive_bayes_model.pkl"
VECTORIZER_PATH = MODEL_DIR / "vectorizer.pkl"
LABEL_ENCODER_PATH = MODEL_DIR / "label_encoder.pkl"

# Konfigurasi preprocessing
PREPROCESS_CONFIG = {
    'remove_punct': True,
    'remove_stopwords': True,
    'stemming': True,
    'lemmatization': False,
    'min_word_length': 2,
    'max_features': 8000
}

# Konfigurasi training
TRAINING_CONFIG = {
    'test_size': 0.2,
    'val_size': 0.1,
    'random_state': 42,
    'smoothing_alpha': 1.0  # Laplace smoothing untuk Naive Bayes
}

# Kategori sentimen
SENTIMENT_LABELS = {
    'negative': 0,
    'neutral': 1,
    'positive': 2
}

# Label mapping berdasarkan rating
RATING_TO_SENTIMENT = {
    1: 'negative',
    2: 'negative',
    3: 'neutral',
    4: 'positive',
    5: 'positive'
}

# Buat direktori jika belum ada
for directory in [DATA_DIR, MODEL_DIR, SPLIT_DATA_DIR]:
    directory.mkdir(parents=True, exist_ok=True)

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))