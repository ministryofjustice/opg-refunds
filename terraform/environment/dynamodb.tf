resource "aws_dynamodb_table" "sessions_public_front" {
  name         = "${local.environment}-refunds-sessions-public-front"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "id"

  attribute {
    name = "id"
    type = "S"
  }

  tags = local.default_tags
}

resource "aws_dynamodb_table" "sessions_caseworker_front" {
  name         = "${local.environment}-refunds-sessions-caseworker-front"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "id"

  attribute {
    name = "id"
    type = "S"
  }

  tags = local.default_tags
}

resource "aws_dynamodb_table" "cronlock" {
  name         = "${local.environment}-refunds-cronlock"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "id"

  attribute {
    name = "id"
    type = "S"
  }

  tags = local.default_tags
}
