FROM laravelsail/php83-composer

# Node + Yarn + system utils
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && apt-get install -y \
    nodejs \
    unzip \
    git \
    vim \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl && \
    npm install -g npm

# ✅ Install MySQL PDO driver
RUN docker-php-ext-install pdo pdo_mysql

# Laravel setup
WORKDIR /var/www
COPY . .

EXPOSE 8000

CMD ["sleep", "infinity"]

