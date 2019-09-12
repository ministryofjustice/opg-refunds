
//-----------------------------------------
// DynamoDB

data "aws_vpc_endpoint_service" "dynamodb" {
  service = "dynamodb"
}

resource "aws_vpc_endpoint" "dynamodb" {
  vpc_id       = aws_default_vpc.default.id
  #service_name = "com.amazonaws.eu-west-1.dynamodb"
  service_name = data.aws_vpc_endpoint_service.dynamodb
  vpc_endpoint_type = "Gateway"

  route_table_ids = aws_route_table.private.*.id
}

//-----------------------------------------
// KMS

data "aws_vpc_endpoint_service" "kms" {
  service = "kms"
}

resource "aws_vpc_endpoint" "kms" {
  vpc_id       = aws_default_vpc.default.id
  #service_name = "com.amazonaws.eu-west-1.kms"
  service_name = data.aws_vpc_endpoint_service.kms
  vpc_endpoint_type = "Interface"

  security_group_ids = [aws_security_group.kms_vpc_endpoint_access.id]
  subnet_ids = aws_subnet.private.*.id

  private_dns_enabled = true
}

resource "aws_security_group" "kms_vpc_endpoint_access" {
  name        = "kms-vpc-endpoint-access"
  vpc_id      = aws_default_vpc.default.id

  ingress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    self = true
  }
}
