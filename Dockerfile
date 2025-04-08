# Use official Python image
FROM python:3.10-slim

# Set working directory in the container
WORKDIR /app

# Copy requirements.txt if it exists and install dependencies
COPY requirements.txt ./ || true
RUN if [ -f requirements.txt ]; then \
        pip install --no-cache-dir -r requirements.txt ; \
    else \
        echo "No requirements.txt found. Skipping dependency installation."; \
    fi

# Copy the rest of the project into the container
COPY . .

# Set the command to run your application
CMD ["python", "app.py"]
