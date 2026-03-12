/**
 * Trillboards Partner API - Node.js Quick Start
 *
 * This example demonstrates the 3-step integration:
 * 1. Get VAST configuration (cache for 1 hour)
 * 2. Fetch VAST from Google Ad Manager (Trillboards not in critical path)
 * 3. Report impressions after ad completion
 *
 * Usage:
 *   TRILLBOARDS_API_KEY=trb_partner_xxx node quick-start.js
 */

const TRILLBOARDS_API = process.env.TRILLBOARDS_API_URL || 'https://api.trillboards.com';
const API_KEY = process.env.TRILLBOARDS_API_KEY;

if (!API_KEY) {
  console.error('Error: Set TRILLBOARDS_API_KEY environment variable');
  process.exit(1);
}

const headers = {
  'Authorization': `Bearer ${API_KEY}`,
  'Content-Type': 'application/json'
};

async function main() {
  try {
    // Step 1: Get VAST configuration (cache this for 1 hour)
    console.log('1. Fetching VAST configuration...');
    const configResponse = await fetch(`${TRILLBOARDS_API}/v1/partner/vast/config`, {
      headers
    });
    const config = await configResponse.json();

    if (!config.success) {
      throw new Error(`Config failed: ${config.message}`);
    }

    console.log('   VAST tag URL:', config.data.vast_tag_template);
    console.log('   Fill rate:', config.data.estimated_fill_rate);

    // Step 2: In production, your player fetches VAST directly from Google
    // This keeps Trillboards out of the critical ad serving path
    const vastUrl = config.data.vast_tag_template
      .replace('[DEVICE_ID]', 'demo-device-001')
      .replace('[TIMESTAMP]', Date.now());

    console.log('\n2. Your player would fetch VAST from:', vastUrl);
    console.log('   (Google IMA SDK handles this automatically)');

    // Step 3: After ad completes, report the impression
    console.log('\n3. Reporting impression after ad completion...');
    const trackingResponse = await fetch(`${TRILLBOARDS_API}/v1/partner/tracking/batch`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        impressions: [{
          device_id: 'demo-device-001',
          ad_id: 'ima_demo_ad_123',
          event: 'complete',
          timestamp: new Date().toISOString(),
          duration_ms: 15000
        }]
      })
    });
    const result = await trackingResponse.json();

    console.log('   Processed:', result.data?.processed || 0);
    console.log('   Proofs:', result.data?.proofs?.length || 0, 'Ed25519 signatures');

    console.log('\nIntegration complete! Your screens are now monetized.');

  } catch (error) {
    console.error('Error:', error.message);
    process.exit(1);
  }
}

main();
