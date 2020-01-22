CREATE TABLE user (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE KEY,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE access_token (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    client_id VARCHAR(128) NOT NULL,
    user_id INT,
    scopes VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE access_token ADD CONSTRAINT FK_access_token_user FOREIGN KEY (user_id)
    REFERENCES user(id) ON UPDATE CASCADE ON DELETE CASCADE;

CREATE TABLE refresh_token (
                              id VARCHAR(128) NOT NULL PRIMARY KEY,
                              access_token_id VARCHAR(128) NOT NULL,
                              expires_at DATETIME NOT NULL,
                              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                              updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE refresh_token ADD CONSTRAINT FK_refresh_token_access_token FOREIGN KEY (access_token_id)
    REFERENCES access_token(id) ON UPDATE CASCADE ON DELETE CASCADE;

CREATE TABLE user_client_consent (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    client_id VARCHAR(128) NOT NULL,
    scopes VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE user_client_consent ADD UNIQUE INDEX user_client_consent_unique (user_id, client_id, scopes);
ALTER TABLE user_client_consent ADD CONSTRAINT FK_user_client_consent_user FOREIGN KEY (user_id)
    REFERENCES user(id) ON UPDATE CASCADE ON DELETE CASCADE;

CREATE TABLE auth_code (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    user_id INT NOT NULL,
    client_id VARCHAR(128) NOT NULL,
    redirect_uri VARCHAR(255) NOT NULL,
    scopes VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
