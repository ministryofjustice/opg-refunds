resource "aws_cloudwatch_log_group" "lpa_refunds" {
  name = "lpa-refunds"

  tags = merge(
    local.default_tags,
    {
      "Name" = "lpa-refunds"
    },
  )
}
