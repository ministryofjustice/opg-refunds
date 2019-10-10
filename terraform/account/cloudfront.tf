# locals {
#   cloudfront_domain  = "${local.account.public_front_dns}.${data.aws_route53_zone.opg_service_justice_gov_uk.name}"
#   cloudfront_aliases = [local.cloudfront_domain, "www.${local.cloudfront_domain}"]
# }

# resource "aws_s3_bucket" "cloudfront_logs" {

#   count  = local.account.has_cloudfront_distribution ? 1 : 0
#   bucket = "lpa-refunds-${terraform.workspace}-cloudfront-logs"
#   acl    = "private"
#   tags   = local.default_tags

#   server_side_encryption_configuration {
#     rule {
#       apply_server_side_encryption_by_default {
#         sse_algorithm = "aws:kms"
#       }
#     }
#   }
# }

# resource "aws_s3_bucket_public_access_block" "cloudfront_logs" {
#   count               = local.account.has_cloudfront_distribution ? 1 : 0
#   bucket              = aws_s3_bucket.cloudfront_logs[0].id
#   block_public_acls   = true
#   block_public_policy = true
# }

# resource "aws_cloudfront_distribution" "public_front" {
#   count = local.account.has_cloudfront_distribution ? 1 : 0
#   origin {
#     domain_name = local.cloudfront_domain
#     origin_id   = "${local.cloudfront_domain}-id"
#     custom_origin_config {
#       origin_ssl_protocols     = ["TLSv1", "TLSv1.1", "TLSv1.2"]
#       origin_protocol_policy   = "https-only"
#       origin_read_timeout      = 30
#       origin_keepalive_timeout = 5
#       http_port                = 80
#       https_port               = 443
#     }
#   }

#   enabled         = true
#   is_ipv6_enabled = true
#   comment         = "Cloudfront cache for ${local.account_name} Created with Terraform"
#   http_version    = "http2"

#   logging_config {
#     include_cookies = true
#     bucket          = aws_s3_bucket.cloudfront_logs[0].bucket_domain_name
#     prefix          = "cf-${local.account_name}"
#   }

#   viewer_certificate {
#     acm_certificate_arn = aws_acm_certificate.cloudfront_public_front[0].arn
#     ssl_support_method  = "sni-only"
#   }

#   aliases = local.cloudfront_aliases

#   default_cache_behavior {
#     allowed_methods  = ["DELETE", "GET", "HEAD", "OPTIONS", "PATCH", "POST", "PUT"]
#     cached_methods   = ["GET", "HEAD"]
#     compress         = false
#     default_ttl      = 0
#     target_origin_id = "${local.cloudfront_domain}-id"
#     trusted_signers  = []

#     forwarded_values {
#       query_string            = true
#       query_string_cache_keys = []
#       headers                 = []
#       cookies {
#         forward           = "whitelist"
#         whitelisted_names = ["ad", "beta", "cookies_enabled", "rs"]
#       }
#     }
#     viewer_protocol_policy = "redirect-to-https"
#   }

#   price_class = "PriceClass_100"

#   restrictions {
#     geo_restriction {
#       locations        = []
#       restriction_type = "none"
#     }
#   }

#   tags = local.default_tags
# }
