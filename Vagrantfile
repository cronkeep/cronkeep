Vagrant.configure("2") do |config|

# Specify the base box
config.vm.box = "ubuntu/trusty64"
config.vm.box_url = "http://cloud-images.ubuntu.com/vagrant/trusty/current/trusty-server-cloudimg-amd64-vagrant-disk1.box"

# Setup port forwarding
config.vm.network :forwarded_port, guest: 80, host: 8080, auto_correct: true

    # Setup synced folder
    config.vm.synced_folder "./", "/var/www/cronkeep", create: true, group: "www-data", owner: "www-data"

    # VM specific configs
    config.vm.provider "virtualbox" do |v|
      v.name = "cronkeep"
      v.customize ["modifyvm", :id, "--memory", "1024"]
    end

    # Shell provisioning
    config.vm.provision "shell" do |s|
      s.path = "provision/setup.sh"
    end
end