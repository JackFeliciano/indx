## Roadmap

The project currently holds a digital identity card with a verified photograph, personal documents, validated contacts, social profiles, a credit score with bank coordinates and access grants. A verifier sees only the fields covered by a grant. The hand-written test runner contains 37 checks.

1. Develop the grant model so each permission clearly identifies the fields a verifier can access.
2. Extend the verification flow to check that a request stays within the scope of its grant.
3. Improve the presentation of identity fields so members and verifiers can understand what each field contains.
4. Expand the tests to cover permitted fields, excluded fields and access decisions.
5. Keep the identity card and verification rules consistent as the set of supported information grows.
