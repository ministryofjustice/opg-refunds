variable "ecs_cluster_name" {
  description = "Name of the ECS Cluster"
  type        = string
}

variable "name" {
  description = "Schedule name if running multiple schedules"
  type        = string
  default     = "hibernation"
}

variable "scale_down_task_count" {
  description = "Minimum running task count during scale down"
  default     = 0
}

variable "scale_down_time" {
  description = "Cron formatted value for scale down trigger"
}

variable "scale_up_time" {
  description = "Cron formatted value for scale up trigger"
}

variable "service_config" {
  description = "Map of services and task count when regually scaled."
  type        = map(string)
}
