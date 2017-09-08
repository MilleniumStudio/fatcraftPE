CREATE TABLE IF NOT EXISTS simpleauth_players (
  email VARCHAR(16) PRIMARY KEY,
  password CHAR(128),
  registerdate INT,
  logindate INT,
  lastip VARCHAR(50),
  ip VARCHAR(50),
  cid BIGINT,
  skinhash VARCHAR(50),
  pin INT
);
