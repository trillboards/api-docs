# Trillboards API Documentation

OpenAPI specifications, code examples, and AI-readable documentation for the [Trillboards DOOH advertising platform](https://trillboards.com).

## API Specifications

| Spec | Description | Interactive Docs |
|------|-------------|-----------------|
| [Partner API](openapi/partner-api.yaml) | Device management, ad serving, webhooks, billing | [View](https://api.trillboards.com/docs/partner) |
| [Programmatic API](openapi/programmatic-api.yaml) | OpenRTB 2.6, VAST, FEIN analytics | [View](https://api.trillboards.com/docs/programmatic) |
| [Attribution API](openapi/attribution-api.yaml) | Store visit attribution, closed-loop measurement | [View](https://api.trillboards.com/docs/attribution) |
| [Proof of Play](openapi/proof-of-play.yaml) | Ed25519 signed impression verification | [View](https://api.trillboards.com/docs/proof-of-play) |
| [OpenRTB Extensions](openapi/openrtb-extensions.yaml) | Trillboards OpenRTB 2.6 extension schemas | — |

## For AI Agents

- **MCP Server**: `https://api.trillboards.com/mcp` (Streamable HTTP)
- **AI-readable docs**: [`llms.txt`](llms.txt)
- **Agent discovery**: [`/.well-known/adagents.json`](https://api.trillboards.com/.well-known/adagents.json)

## Quick Start Examples

- [Node.js](examples/node/quick-start.js)
- [Python](examples/python/quick_start.py)
- [cURL](examples/curl/quick-start.sh)
- [PHP](examples/php/TrillboardsPartner.php)

## Links

- [Trillboards](https://trillboards.com) — Main website
- [API Console](https://api.trillboards.com/docs/partner) — Interactive API explorer
- [MCP on Smithery](https://smithery.ai/servers/trillboards/dooh-advertising) — AI agent marketplace
- [dooh-agent-example](https://github.com/trillboards/dooh-agent-example) — Complete agent example
- [proof-of-play-verifier](https://github.com/trillboards/proof-of-play-verifier) — Independent verification tool

## License

MIT
