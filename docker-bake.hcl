variable "TAG" {
    default = "latest"
}

group "default" {
    targets = ["api", "web"]
}

target "api" {
    context = "./api"
    tags = [
        "ghcr.io/chitoku-k/homochecker/api:latest",
        "ghcr.io/chitoku-k/homochecker/api:${TAG}",
    ]
}

target "web" {
    context = "."
    tags = [
        "ghcr.io/chitoku-k/homochecker/web:latest",
        "ghcr.io/chitoku-k/homochecker/web:${TAG}",
    ]
}
