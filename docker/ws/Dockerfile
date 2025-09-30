FROM node:20-alpine

WORKDIR /app

RUN apk add --no-cache build-base python3

COPY package.json package-lock.json ./
RUN npm ci --production

COPY . .

EXPOSE 6001
CMD ["node", "server.js"]
