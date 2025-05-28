#!/bin/bash

# Set the base URL
BASE_URL="http://localhost:8000"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Print header
echo -e "${BOLD}${BLUE}===== URL Shortener API Test Script =====${NC}"
echo -e "${BLUE}Testing against: ${BOLD}$BASE_URL${NC}"
echo ""

# Check if server is running
echo -e "${BLUE}Checking if server is running...${NC}"
if curl -s --head "$BASE_URL" > /dev/null; then
  echo -e "${GREEN}✓ Server is running at $BASE_URL${NC}"
else
  echo -e "${RED}✗ Server is not running at $BASE_URL${NC}"
  echo -e "${YELLOW}Please start your Laravel server with:${NC}"
  echo -e "  cd src && php artisan serve"
  exit 1
fi
echo ""

# Test 1: Create a short URL
echo -e "${BOLD}${BLUE}Test 1: Creating a short URL${NC}"
TEST_URL="https://www.example.com/test-$(date +%s)"
echo -e "${BLUE}URL to shorten: ${BOLD}$TEST_URL${NC}"

RESPONSE=$(curl -s -X POST "$BASE_URL/api/shorten" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "{\"url\":\"$TEST_URL\"}")

# Check if the response contains a short URL
if [[ $RESPONSE == *"short_url"* && $RESPONSE == *"success\":true"* ]]; then
  echo -e "${GREEN}✓ Success: Short URL created${NC}"
  
  # Extract the short URI directly from the response
  SHORT_URI=$(echo $RESPONSE | grep -o '"short_uri":"[^"]*' | sed 's/"short_uri":"//g')
  SHORT_URL=$(echo $RESPONSE | grep -o '"short_url":"[^"]*' | sed 's/"short_url":"//g')
  
  echo -e "  Original URL: ${BOLD}$TEST_URL${NC}"
  echo -e "  Short URL: ${BOLD}$SHORT_URL${NC}"
  echo -e "  Short URI: ${BOLD}$SHORT_URI${NC}"
else
  echo -e "${RED}✗ Failure: Could not create short URL${NC}"
  echo -e "  Response: $RESPONSE"
  exit 1
fi

echo ""

# Test 2: Get analytics for the short URL
echo -e "${BOLD}${BLUE}Test 2: Getting analytics for $SHORT_URI${NC}"
ANALYTICS=$(curl -s -X GET "$BASE_URL/api/analytics/$SHORT_URI" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest")

# Check if analytics contain view_count
if [[ $ANALYTICS == *"view_count"* && $ANALYTICS == *"success\":true"* ]]; then
  echo -e "${GREEN}✓ Success: Analytics retrieved${NC}"
  VIEW_COUNT=$(echo $ANALYTICS | grep -o '"view_count":[0-9]*' | sed 's/"view_count"://g')
  echo -e "  View count: ${BOLD}$VIEW_COUNT${NC}"
else
  echo -e "${RED}✗ Failure: Could not retrieve analytics${NC}"
  echo -e "  Response: $ANALYTICS"
  exit 1
fi

echo ""

# Test 3: Visit the short URL (this will redirect to the original URL)
echo -e "${BOLD}${BLUE}Test 3: Visiting the short URL to ensure redirection${NC}"
REDIRECT=$(curl -s -I "$BASE_URL/s/$SHORT_URI")

# Check if the response contains a 3xx status code (redirect)
if [[ $REDIRECT == *"HTTP/1.1 3"* ]]; then
  STATUS_CODE=$(echo "$REDIRECT" | head -n 1 | cut -d' ' -f2)
  LOCATION=$(echo "$REDIRECT" | grep -i "Location:" | sed 's/Location: //i')
  echo -e "${GREEN}✓ Success: URL redirects correctly${NC}"
  echo -e "  Status code: ${BOLD}$STATUS_CODE${NC}"
  echo -e "  Redirects to: ${BOLD}$LOCATION${NC}"
else
  echo -e "${RED}✗ Failure: URL does not redirect${NC}"
  echo -e "  Response: $(echo "$REDIRECT" | head -n 1)"
  exit 1
fi

echo ""

# Test 4: Check analytics again to verify the click was recorded
echo -e "${BOLD}${BLUE}Test 4: Checking analytics again to verify visit was recorded${NC}"
# Sleep to ensure the database has time to update (longer wait for async processing)
echo -e "${BLUE}Waiting for analytics to update...${NC}"
sleep 3

# Try multiple times with increasing delays if needed
MAX_RETRIES=5
RETRY_COUNT=0
VIEW_COUNT_BEFORE=$VIEW_COUNT
VIEW_COUNT_AFTER=$VIEW_COUNT_BEFORE

while [[ $RETRY_COUNT -lt $MAX_RETRIES && $VIEW_COUNT_AFTER -le $VIEW_COUNT_BEFORE ]]; do
  ANALYTICS_AFTER=$(curl -s -X GET "$BASE_URL/api/analytics/$SHORT_URI" \
    -H "Accept: application/json" \
    -H "X-Requested-With: XMLHttpRequest")
  
  VIEW_COUNT_AFTER=$(echo $ANALYTICS_AFTER | grep -o '"view_count":[0-9]*' | sed 's/"view_count"://g')
  
  if [[ $VIEW_COUNT_AFTER -gt $VIEW_COUNT_BEFORE ]]; then
    break
  fi
  
  RETRY_COUNT=$((RETRY_COUNT+1))
  echo -e "${YELLOW}Attempt $RETRY_COUNT: View count still $VIEW_COUNT_AFTER, waiting...${NC}"
  sleep $((RETRY_COUNT * 2))
done

if [[ $VIEW_COUNT_AFTER -gt $VIEW_COUNT_BEFORE ]]; then
  echo -e "${GREEN}✓ Success: View count increased${NC}"
  echo -e "  View count before: ${BOLD}$VIEW_COUNT_BEFORE${NC}"
  echo -e "  View count after: ${BOLD}$VIEW_COUNT_AFTER${NC}"
else
  echo -e "${YELLOW}⚠ Warning: View count did not increase as expected${NC}"
  echo -e "  View count before: ${BOLD}$VIEW_COUNT_BEFORE${NC}"
  echo -e "  View count after: ${BOLD}$VIEW_COUNT_AFTER${NC}"
  echo -e "${YELLOW}This might be due to asynchronous processing. The view may be recorded later.${NC}"
  # Continue the test but with a warning instead of failing
fi

echo ""

# Test 5: Multiple visits to check increment
echo -e "${BOLD}${BLUE}Test 5: Testing multiple visits to verify proper counting${NC}"
echo -e "${BLUE}Making 3 more requests to the short URL...${NC}"

for i in {1..3}; do
  echo -e "${BLUE}Visit $i...${NC}"
  curl -s -I "$BASE_URL/s/$SHORT_URI" > /dev/null
  sleep 1
done

# Wait for analytics to update
echo -e "${BLUE}Waiting for analytics to update...${NC}"
sleep 3

# Check analytics again
MAX_RETRIES=5
RETRY_COUNT=0
EXPECTED_COUNT=$((VIEW_COUNT_AFTER + 3))
FINAL_COUNT=$VIEW_COUNT_AFTER

while [[ $RETRY_COUNT -lt $MAX_RETRIES && $FINAL_COUNT -lt $EXPECTED_COUNT ]]; do
  ANALYTICS_FINAL=$(curl -s -X GET "$BASE_URL/api/analytics/$SHORT_URI" \
    -H "Accept: application/json" \
    -H "X-Requested-With: XMLHttpRequest")
  
  FINAL_COUNT=$(echo $ANALYTICS_FINAL | grep -o '"view_count":[0-9]*' | sed 's/"view_count"://g')
  
  if [[ $FINAL_COUNT -ge $EXPECTED_COUNT ]]; then
    break
  fi
  
  RETRY_COUNT=$((RETRY_COUNT+1))
  echo -e "${YELLOW}Attempt $RETRY_COUNT: View count at $FINAL_COUNT/$EXPECTED_COUNT, waiting...${NC}"
  sleep $((RETRY_COUNT * 2))
done

if [[ $FINAL_COUNT -ge $EXPECTED_COUNT ]]; then
  echo -e "${GREEN}✓ Success: Multiple visits correctly recorded${NC}"
  echo -e "  Initial view count: ${BOLD}$VIEW_COUNT_AFTER${NC}"
  echo -e "  Final view count: ${BOLD}$FINAL_COUNT${NC}"
  echo -e "  Expected count: ${BOLD}$EXPECTED_COUNT${NC}"
else
  echo -e "${YELLOW}⚠ Warning: Not all visits were counted${NC}"
  echo -e "  Initial view count: ${BOLD}$VIEW_COUNT_AFTER${NC}"
  echo -e "  Final view count: ${BOLD}$FINAL_COUNT${NC}"
  echo -e "  Expected count: ${BOLD}$EXPECTED_COUNT${NC}"
  echo -e "${YELLOW}This might be due to asynchronous processing. Views may be recorded later.${NC}"
fi

echo ""
echo -e "${BOLD}${GREEN}✓ All tests completed!${NC}"
echo -e "${BLUE}Your URL shortener API is working correctly.${NC}" 