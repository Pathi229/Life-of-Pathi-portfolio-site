# Docker workflow verification

Verified on 7 October 2026 using Docker Compose with Linux containers in the cloud environment, starting from an extracted source ZIP with no environment file, database, dependencies or generated assets.

Successful checks:

- Compose configuration validation and PHP image build.
- Composer installation of the exact locked packages with all required PHP extensions.
- Frontend installation and production build using the included Node tool service.
- Application key generation, SQLite migrations, demo seed and Filament asset publication.
- Interactive `pathi:admin` account creation with a hidden password.
- 25 application tests passing with 164 assertions; Pint passing for 60 PHP files.
- Website returning HTTP 200 and browser login to the admin dashboard with the newly created account.
- Container shutdown and restart preserving the database and administrator account.

The cloud network blocks GitHub archive downloads, so verification used Composer's source installation mode and a previously downloaded cache of the exact locked Git revisions. A temporary override provided this environment's proxy and trusted CA. Those overrides and caches are **not** included in the ZIP. The Windows guide uses normal archive downloads (`--prefer-dist`) for a smaller, faster installation on ordinary Internet connections.

Docker Desktop's Windows UI and Windows filesystem integration were not directly tested here. The supplied configuration uses standard bind mounts, Linux PHP and Node containers, an SQLite file inside the project, and a host port bound to loopback. It requires no host PHP, Composer, Node or separate database server. No deployment was performed.
