## Maintenance Site
to copy maintenance site to the s3 bucket website run

```
aws s3 sync ./maintenance s3://claim-power-of-attorney-refund.service.gov.uk
```

you must run this with operator role for moj-refunds-development
