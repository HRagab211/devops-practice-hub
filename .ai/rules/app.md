---
paths:
  - 'app/**'
---

# App

## Keep report ownership and private storage boundaries
Create report records for the authenticated user before dispatch; retries reuse the report ID and deterministic private path. Download through the TaskReport policy and stored disk/path only. Never expose reports through the public storage link. Readiness is registered outside web/session middleware; keep /up dependency-free.
