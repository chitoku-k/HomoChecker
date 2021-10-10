group "default" {
    targets = ["api", "web"]
}

target "api" {
    context = "./api"
    tags = ["container.chitoku.jp/chitoku-k/homochecker/api"]
}

target "web" {
    context = "."
    tags = ["container.chitoku.jp/chitoku-k/homochecker/web"]
}
