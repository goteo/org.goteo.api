#!/bin/sh

set -e

echo "Creating the database if it does not exist..."
php bin/console doctrine:database:create --if-not-exists

# Check if migrations need to be applied
if ! php bin/console doctrine:migrations:up-to-date --quiet; then
  echo "Applying migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction
fi

# Check if FORCE_SCHEMA_UPDATE is set to true
if [ "$FORCE_SCHEMA_UPDATE" = "true" ]; then
  echo "Forcing schema update..."
  php bin/console doctrine:schema:update --force
fi

# Execute the CMD from the Dockerfile
exec "$@"
