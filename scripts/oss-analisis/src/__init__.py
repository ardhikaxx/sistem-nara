"""
Package untuk analisis sentimen review OSS Indonesia
"""

from .preprocess import TextPreprocessor, prepare_data_for_analysis
from .naive_bayes import NaiveBayesClassifier
from .train import train_model, analyze_training_results
from .evaluate import evaluate_model, visualize_results
from .predict import SentimentPredictor, predict_example, predict_from_csv

__version__ = "1.0.0"
__all__ = [
    'TextPreprocessor',
    'prepare_data_for_analysis',
    'NaiveBayesClassifier',
    'train_model',
    'analyze_training_results',
    'evaluate_model',
    'visualize_results',
    'SentimentPredictor',
    'predict_example',
    'predict_from_csv'
]