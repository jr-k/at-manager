echo "Let's do this"

ssh foo@dummy-ssh-server <<-'ENDSSH'
    echo "Hi" >> /tmp/at-manager
ENDSSH