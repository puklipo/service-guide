ARG VERSION=php83

FROM laravelphp/vapor:${VERSION}

COPY . /var/task
