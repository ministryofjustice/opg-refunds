# Refunds Migration Plan

## 

### Import ASG resources to manage

```bash
tf import aws_autoscaling_group.public_front front-<STACK_NAME>
tf import aws_autoscaling_group.caseworker_front caseworker-front-<STACK_NAME>
tf import aws_autoscaling_group.caseworker_api caseworker-api-<STACK_NAME>
```

### Take down stack

```
./old_account_down.sh
```
This will set ASGs to 0 for the target stack, then wait for user input before stopping the db instances.

There are expected to be user input prompts for aws-vault.


## Roll back
### Bring up stack

```
./old_account_up.sh
```

This will set ASGs to their normal running state, and start DB instances.
