resource "aws_cloudwatch_log_group" "lpa_refunds" {
  name              = "lpa-refunds"
  retention_in_days = local.account.retention_in_days

  tags = merge(
    local.default_tags,
    {
      "Name" = "lpa-refunds"
    },
  )
}
