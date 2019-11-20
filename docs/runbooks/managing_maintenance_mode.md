# Managing Maintenance Mode

## How to turn maintenance mode on and off for production

### Update maintenance switch
Log into AWS Console and assume the `operator` role in the `moj-refunds-production`account.

Go to the System Manager Parameters page, and click on the parameter `production_enable_maintenance`.

https://eu-west-1.console.aws.amazon.com/systems-manager/parameters?region=eu-west-1

Click edit and set value to `true` (lower case is important).

### Enable maintenance
From your machine ensure you have the correct workspace and role set
```
cd terraform/environment
cat .envrc 
```
`TF_VAR_default_role` should be `operator`
`TF_WORKSPACE` should be `production`

If any changes are made, make sure they are applied
```
dirnev allow
```

Run a terraform apply
```
aws-vault exec identity -- terraform apply
```
Optionally, you can deploy a specific image tag even while in maintenance mode

```
aws-vault exec identity -- terraform apply -var container_version=LPA-3470-2e3228e
```

Image tags can be obtained from ECR in the maintenance account

The execution of the CircleCI pipeline will not change the maintenance mode state until maintenance mode is turned off.

To turn off maintenance mode, update the SSM parameter to `false` and run a terraform apply again.


## How to turn maintenance mode on and off for other environments

Instructions are the same as above, with these differences

In the System Manager Parameters page, the parameter is `<ENVIRONMENT-NAME>_enable_maintenance`, where `<ENVIRONMENT-NAME>` is the name of the environment you want to put into maintenance.

From your machine when setting the workspace
`TF_WORKSPACE` should be the name of the workspace the environment live in
