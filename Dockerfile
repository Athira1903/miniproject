# Step 1: Clone the repo
git clone https://github.com/Athira1903/miniproject.git

# Step 2: Go into the project folder
cd miniproject

# Step 3: Confirm that Dockerfile exists and is inside src/
ls src
# You should see a file named Dockerfile listed here

# Step 4: Build the Docker image (corrected command)
docker build -f src/Dockerfile -t miniproject-image .
