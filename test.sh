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
# Sleep briefly to ensure the database has time to update
sleep 1
ANALYTICS_AFTER=$(curl -s -X GET "$BASE_URL/api/analytics/$SHORT_URI" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest")
VIEW_COUNT_BEFORE=$VIEW_COUNT
VIEW_COUNT_AFTER=$(echo $ANALYTICS_AFTER | grep -o '"view_count":[0-9]*' | sed 's/"view_count"://g')

if [[ $VIEW_COUNT_AFTER > $VIEW_COUNT_BEFORE ]]; then
  echo -e "${GREEN}✓ Success: View count increased${NC}"
  echo -e "  View count before: ${BOLD}$VIEW_COUNT_BEFORE${NC}"
  echo -e "  View count after: ${BOLD}$VIEW_COUNT_AFTER${NC}"
else
  echo -e "${RED}✗ Failure: View count did not increase${NC}"
  echo -e "  View count before: ${BOLD}$VIEW_COUNT_BEFORE${NC}"
  echo -e "  View count after: ${BOLD}$VIEW_COUNT_AFTER${NC}"
  echo -e "${YELLOW}Note: This might be due to a caching issue or delayed database update${NC}"
  exit 1
fi

echo ""
echo -e "${BOLD}${GREEN}✓ All tests passed successfully!${NC}"
echo -e "${BLUE}Your URL shortener API is working correctly.${NC}" 