ARG VERSION=php84

FROM laravelphp/vapor:${VERSION}

COPY . /var/task
