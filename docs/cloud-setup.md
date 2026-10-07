# Cloud development

Use the existing `/workspace/Life-of-Pathi-portfolio-site` checkout. Each cloud task is isolated; do not create an additional worktree unless explicitly requested.

The cloud host has Node.js 24 and Chromium but no system PHP. Docker supplies PHP 8.4 with the required extensions. Standard hosts can follow README or Docker Compose. The cloud egress proxy requires the existing host CA bundle and a resolvable proxy hostname inside the build. TLS and Debian package signature verification must remain enabled.

For this cloud machine, the prepared `pathi-php` image and vendor/node_modules dependencies are retained filesystem state. Live containers/processes may need restarting. Use the saved environment startup instructions to start a container with the existing checkout mounted as `/app` and the host CA bundle mounted read-only. No secret values are saved.

The GitHub API archive destination is blocked under the original allowlist; `composer install --prefer-source` is the supported source-install fallback. This is slower but retains TLS verification and uses the lockfile’s exact source references. Native GitHub access and package registries are sufficient; no GitHub token is needed for these public dependencies.
