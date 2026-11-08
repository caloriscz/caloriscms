# Disposable compatibility probe, separate from the running app image.
ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-cli
# PHP 8.0's final image uses Bullseye. Use a dated package snapshot for that
# obsolete probe only; current mirrors no longer serve its indexed ICU packages.
# Do not deploy this compatibility image as an application runtime.
RUN if grep -q 'VERSION_CODENAME=bullseye' /etc/os-release; then \
        printf 'deb [check-valid-until=no] https://snapshot.debian.org/archive/debian/20231101T000000Z bullseye main\n' > /etc/apt/sources.list; \
    fi \
    && apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev \
    && docker-php-ext-install intl pdo_mysql \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /work
