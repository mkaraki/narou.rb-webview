FROM oven/bun:latest AS frontend

WORKDIR /app

COPY frontend/package.json frontend/bun.lock /app/

RUN bun install

COPY --exclude=dist --exclude=node_modules frontend /app

RUN bun run build

FROM rust:1.91-trixie AS build

WORKDIR /app

COPY Cargo.lock Cargo.toml /app/
COPY src /app/src

RUN cargo build --release --bin narourb_webview

FROM debian:trixie-slim

WORKDIR /app
COPY --from=build /app/target/release/narourb_webview /app/
COPY --from=frontend /app/dist /app/frontend/dist

VOLUME /opt/narou
EXPOSE 3001

ENTRYPOINT ["/app/narourb_webview"]
