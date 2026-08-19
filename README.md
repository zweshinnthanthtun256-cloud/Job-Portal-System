# Job Portal System — Docker + Laravel (Sail) + Inertia (Vue) + PostgreSQL setup helper

This repository was empty. I added a set of helper files so you can initialize a Laravel app inside this repo and install Sail, Breeze (Inertia + Vue), and PostgreSQL support using Docker.

What I added

- setup.sh — opinionated script that bootstraps a Laravel project in this repo, installs Sail, enables PostgreSQL, installs Breeze (Inertia + Vue), and builds assets inside Sail.
- README.md — concise instructions for how to run the setup script and what to expect.
- .gitignore — a Laravel-appropriate .gitignore so generated files are not accidentally committed.
- LICENSE — MIT license placeholder.

Important notes

- I cannot run composer, artisan, or npm for you from here. The script (`setup.sh`) runs those commands on your machine (or inside Docker). Run it locally where Docker is installed.
- The script checks for an existing `composer.json` and will abort to avoid overwriting an existing project.
- The script will create many files (the full Laravel app). After you run it and confirm everything looks good, commit the generated files and push them; or share errors here and I'll help debug.

Quick start

1) Make the script executable: `chmod +x setup.sh`
2) Run the script: `./setup.sh`
3) Follow the script prompts. After the script finishes, your app should be running in Docker with PostgreSQL and Breeze (Inertia + Vue) scaffolding installed.

If you want, I can then create a feature branch with the generated scaffolding committed, or I can create a PR with further configuration (Vite HMR, .env tweaks, README improvements).