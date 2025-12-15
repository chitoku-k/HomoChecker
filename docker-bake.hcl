group "default" {
    targets = ["api", "web"]
}

target "api" {
    context = "./api"
}

target "web" {
    context = "./web"
}
