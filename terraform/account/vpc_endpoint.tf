
//-----------------------------------------
// Endpoint Access Policy

data "aws_iam_policy_document" "enforce_endpoint_access" {
  statement {
    effect    = "Deny"
    actions   = ["*"]
    resources = ["*"]
    condition {
      test     = "StringNotEquals"
      variable = "aws:sourceVpc"
      values   = [aws_default_vpc.default.id]
    }
  }
}

resource "aws_iam_policy" "enforce_endpoint_access" {
  name        = "enforce_vpc_endpoint_access"
  description = "Forces traffic to originate from the default VPC"
  policy      = data.aws_iam_policy_document.enforce_endpoint_access.json
}

//-----------------------------------------
// DynamoDB Endpoint

data "aws_vpc_endpoint_service" "dynamodb" {
  service = "dynamodb"
}

resource "aws_vpc_endpoint" "dynamodb" {
  vpc_id            = aws_default_vpc.default.id
  service_name      = data.aws_vpc_endpoint_service.dynamodb.service_name
  vpc_endpoint_type = "Gateway"

  route_table_ids = aws_route_table.private.*.id

  tags = merge(
    local.default_tags,
    map("Name", "DynamoDB Gateway")
  )
}

//-----------------------------------------
// KMS Endpoint

data "aws_vpc_endpoint_service" "kms" {
  service = "kms"
}

resource "aws_vpc_endpoint" "kms" {
  vpc_id            = aws_default_vpc.default.id
  service_name      = data.aws_vpc_endpoint_service.kms.service_name
  vpc_endpoint_type = "Interface"

  security_group_ids = [aws_security_group.kms_vpc_endpoint.id]
  subnet_ids         = aws_subnet.private.*.id

  private_dns_enabled = true

  tags = local.default_tags
}

resource "aws_security_group" "kms_vpc_endpoint" {
  name   = "kms-vpc-endpoint"
  vpc_id = aws_default_vpc.default.id

  ingress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = [aws_default_vpc.default.cidr_block]
  }

  tags = merge(
    local.default_tags,
    map("Name", "KMS Gateway")
  )
}
