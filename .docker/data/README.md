# .docker/data

Please map persistent volumes to this directory on the servers.

If a container needs to persist data between restarts you can map the relevant files in the container to ``docker/data/<container-name>`.

## RabbitMQ example
If you are using RabbitMQ running in a container as a message broker you need to configure a persistent volume for RabbitMQs data directory to avoid losing message on container restarts.

```yaml
# docker-compose.server.override.yml

services:
  rabbit:
    image: rabbitmq:3.9-management-alpine
    hostname: "${COMPOSE_PROJECT_NAME}"
    networks:
      - app
      - frontend
    environment:
      - "RABBITMQ_DEFAULT_USER=${RABBITMQ_USER}"
      - "RABBITMQ_DEFAULT_PASS=${RABBITMQ_PASSWORD}"
      - "RABBITMQ_ERLANG_COOKIE=${RABBITMQ_ERLANG_COOKIE}"
    volumes:
      - ".docker/data/rabbitmq:/var/lib/rabbitmq/mnesia/"
```
