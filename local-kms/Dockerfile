from registry.service.opg.digital/opg-golang-alpine

ARG LIB_VERSION="1.0.0"
ARG LIB_PATH="$GOPATH/src/github.com/NSmithUK/local-kms-go"

RUN apk update && \
    apk add ca-certificates openssl && \
    update-ca-certificates && \
    mkdir -p ${LIB_PATH} && \
    wget -qO- https://github.com/NSmithUK/local-kms-go/archive/${LIB_VERSION}.tar.gz | tar xz -C ${LIB_PATH} --strip-components=1 && \
    cd ${LIB_PATH} && \
    dep ensure && \
    go install

ENV PORT=8080 GIN_MODE=release

CMD local-kms-go
