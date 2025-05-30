# predictor.py
import pandas as pd
import joblib
import json
from datetime import datetime

# Load model and data
model = joblib.load('model/predictor.pkl')
df = pd.read_csv('data/attendance.csv')

# Get current semester
current_month = datetime.now().month
current_semester = 'WINTER SEMESTER' if current_month >= 1 and current_month <= 6 else 'SUMMER SEMESTER'

# Filter data for current semester
df = df[df['term'] == current_semester]

# Feature engineering
df['attendance_percent'] = df['attended_classes'] / df['total_classes']
df['avg_per_week'] = df['attended_classes'] / df['weeks_passed']

# Prediction
X = df[['total_classes', 'attended_classes', 'weeks_passed', 'attendance_percent', 'avg_per_week']]
predictions = model.predict(X)

# Result
df['prediction'] = predictions
df['status'] = df['prediction'].map({1: "Safe", 0: "Risk"})

# Final output
output = df[['student_id', 'name', 'attendance_percent', 'status']]
print(output.to_json(orient='records'))
