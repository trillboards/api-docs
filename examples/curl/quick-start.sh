#!/bin/bash
#
# Trillboards Partner API - curl Quick Start
#
# This script demonstrates the 3-step integration:
# 1. Get VAST configuration (cache for 1 hour)
# 2. Fetch VAST from Google Ad Manager (Trillboards not in critical path)
# 3. Report impressions after ad completion
#
# Usage:
#   export TRILLBOARDS_API_KEY=trb_partner_xxx
#   ./quick-start.sh
#

set -e

API_BASE="${TRILLBOARDS_API_URL:-https://api.trillboards.com}"
API_KEY="${TRILLBOARDS_API_KEY}"

if [ -z "$API_KEY" ]; then
    echo "Error: Set TRILLBOARDS_API_KEY environment variable"
    exit 1
fi

echo "=========================================="
echo "Trillboards Partner API - Quick Start"
echo "=========================================="
echo ""

# Step 1: Get VAST configuration
echo "1. Fetching VAST configuration..."
echo ""
CONFIG=$(curl -s -H "Authorization: Bearer $API_KEY" \
    "$API_BASE/v1/partner/vast/config")

echo "Response:"
echo "$CONFIG" | jq '.' 2>/dev/null || echo "$CONFIG"
echo ""

# Extract VAST tag URL (requires jq)
if command -v jq &> /dev/null; then
    VAST_URL=$(echo "$CONFIG" | jq -r '.data.vast_tag_template // "N/A"')
    echo "VAST Tag URL: $VAST_URL"
fi
echo ""

# Step 2: In production, your player fetches VAST directly from Google
echo "2. Your player fetches VAST directly from Google Ad Manager"
echo "   (Google IMA SDK handles this - Trillboards not in critical path)"
echo ""

# Step 3: Report impression
echo "3. Reporting impression after ad completion..."
echo ""
TIMESTAMP=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

RESULT=$(curl -s -X POST \
    -H "Authorization: Bearer $API_KEY" \
    -H "Content-Type: application/json" \
    -d "{
        \"impressions\": [{
            \"device_id\": \"demo-device-001\",
            \"ad_id\": \"ima_demo_ad_123\",
            \"event\": \"complete\",
            \"timestamp\": \"$TIMESTAMP\",
            \"duration_ms\": 15000
        }]
    }" \
    "$API_BASE/v1/partner/tracking/batch")

echo "Response:"
echo "$RESULT" | jq '.' 2>/dev/null || echo "$RESULT"
echo ""

echo "=========================================="
echo "Integration complete!"
echo "=========================================="
