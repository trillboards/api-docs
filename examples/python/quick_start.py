#!/usr/bin/env python3
"""
Trillboards Partner API - Python Quick Start

This example demonstrates the 3-step integration:
1. Get VAST configuration (cache for 1 hour)
2. Fetch VAST from Google Ad Manager (Trillboards not in critical path)
3. Report impressions after ad completion

Usage:
    TRILLBOARDS_API_KEY=trb_partner_xxx python quick_start.py
"""

import os
import sys
from datetime import datetime

try:
    import requests
except ImportError:
    print("Error: Install requests library: pip install requests")
    sys.exit(1)

TRILLBOARDS_API = os.environ.get('TRILLBOARDS_API_URL', 'https://api.trillboards.com')
API_KEY = os.environ.get('TRILLBOARDS_API_KEY')

if not API_KEY:
    print('Error: Set TRILLBOARDS_API_KEY environment variable')
    sys.exit(1)

headers = {
    'Authorization': f'Bearer {API_KEY}',
    'Content-Type': 'application/json'
}


def main():
    # Step 1: Get VAST configuration (cache this for 1 hour)
    print('1. Fetching VAST configuration...')
    config_response = requests.get(
        f'{TRILLBOARDS_API}/v1/partner/vast/config',
        headers=headers
    )
    config = config_response.json()

    if not config.get('success'):
        raise Exception(f"Config failed: {config.get('message')}")

    print(f"   VAST tag URL: {config['data']['vast_tag_template']}")
    print(f"   Fill rate: {config['data'].get('estimated_fill_rate', 'N/A')}")

    # Step 2: In production, your player fetches VAST directly from Google
    # This keeps Trillboards out of the critical ad serving path
    vast_url = config['data']['vast_tag_template'] \
        .replace('[DEVICE_ID]', 'demo-device-001') \
        .replace('[TIMESTAMP]', str(int(datetime.now().timestamp() * 1000)))

    print(f'\n2. Your player would fetch VAST from: {vast_url}')
    print('   (Google IMA SDK handles this automatically)')

    # Step 3: After ad completes, report the impression
    print('\n3. Reporting impression after ad completion...')
    tracking_response = requests.post(
        f'{TRILLBOARDS_API}/v1/partner/tracking/batch',
        headers=headers,
        json={
            'impressions': [{
                'device_id': 'demo-device-001',
                'ad_id': 'ima_demo_ad_123',
                'event': 'complete',
                'timestamp': datetime.utcnow().isoformat() + 'Z',
                'duration_ms': 15000
            }]
        }
    )
    result = tracking_response.json()

    processed = result.get('data', {}).get('processed', 0)
    proofs = len(result.get('data', {}).get('proofs', []))
    print(f'   Processed: {processed}')
    print(f'   Proofs: {proofs} Ed25519 signatures')

    print('\nIntegration complete! Your screens are now monetized.')


if __name__ == '__main__':
    main()
