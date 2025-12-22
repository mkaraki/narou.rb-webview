FROM oven/bun:latest AS frontend

WORKDIR /app

COPY frontend/package.json frontend/bun.lock /app/

RUN bun install

COPY --exclude=dist --exclude=node_modules frontend /app

RUN bun run build

FROM rust:1.91-alpine AS build

WORKDIR /app

RUN apk update &&\
    apk add --no-cache \
    openssl-dev \
    openssl-libs-static

COPY Cargo.lock Cargo.toml /app/

COPY src /app/src
RUN cargo build --release --target=x86_64-unknown-linux-musl --bin narourb_webview

FROM gcr.io/distroless/static-debian13:nonroot

USER nonroot

WORKDIR /app
COPY --from=build /app/target/x86_64-unknown-linux-musl/release/narourb_webview /app/
COPY --from=frontend /app/dist /app/frontend/dist

VOLUME /opt/narou
EXPOSE 3001

ENTRYPOINT ["/app/narourb_webview"]
