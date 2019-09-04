resource "aws_dynamodb_table" "sessions_public_front" {
  name         = "refunds-sessions-public-front-${local.environment}"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "id"

  attribute {
    name = "id"
    type = "S"
  }

  tags = local.default_tags
}
