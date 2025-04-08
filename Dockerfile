# Use a base image
FROM python:3.10-slim

# Set working directory
WORKDIR /app

# Copy requirements if any
COPY requirements.txt .

# Install dependencies
RUN pip install --no-cache-dir -r requirements.txt

# Copy the project files
COPY . .

# Run the app (replace with your actual entry point)
CMD ["python", "main.py"]
