group "default" {
    targets = ["api", "web"]
}

target "api" {
    context = "./api"
    target = "production"
}

target "web" {
    context = "./web"
    target = "production"
}
