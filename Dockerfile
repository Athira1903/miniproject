# Use official Python image
FROM python:3.10-slim

# Set working directory in the container
WORKDIR /app

# Copy dependency list and install
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy the whole project into the container
COPY . .

# Set the command to run your app
CMD ["python", "src/app.py"]
