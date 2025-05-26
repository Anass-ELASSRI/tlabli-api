#!/bin/bash

# Run Laravel server in background
php artisan serve &

# Run npm dev server (frontend)
npm run dev