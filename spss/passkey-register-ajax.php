<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const webauthnResponse = document.getElementById('webauthnResponse');

        // Ambil daftar NIS dari passkey-register.php
        fetch(window.location.href)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    alert('Error: ' + data.message);
                    return;
                }
                const select = document.getElementById('nis');
                data.nis_options.forEach(nis => {
                    const option = document.createElement('option');
                    option.value = nis;
                    option.text = nis;
                    select.appendChild(option);
                });
            })
            .catch(err => alert('Gagal memuat daftar NIS: ' + err.message));

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const nis = document.getElementById('nis').value;

            if (!nis) {
                alert('Pilih NIS terlebih dahulu!');
                return;
            }

            const formData = new FormData(form);
            const response = await fetch('passkey-register-ajax.php', {
                method: 'POST',
                body: formData
            });

            console.log('Response status:', response.status);
            console.log('Response text:', await response.text());

            const result = await response.json();
            if (result.status === 'error') {
                alert('Error: ' + result.message);
                return;
            }

            const publicKey = result;
            publicKey.publicKey.challenge = Uint8Array.from(
                atob(publicKey.publicKey.challenge.replace(/_/g, '+').replace(/-/g, '/'))
                    .split('').map(c => c.charCodeAt(0))
            );
            publicKey.publicKey.user.id = Uint8Array.from(
                atob(publicKey.publicKey.user.id.replace(/_/g, '+').replace(/-/g, '/'))
                    .split('').map(c => c.charCodeAt(0))
            );

            try {
                const credential = await navigator.credentials.create({ publicKey });
                const responseData = {
                    id: credential.id,
                    rawId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
                    type: credential.type,
                    response: {
                        clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON))),
                        attestationObject: btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject)))
                    }
                };

                const verifyResponse = await fetch('verify-passkey-register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(responseData)
                });

                const verifyResult = await verifyResponse.json();
                if (verifyResult.status === 'success') {
                    alert('Passkey berhasil didaftarkan!');
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Error: ' + (verifyResult.message || 'Verifikasi gagal'));
                }
            } catch (err) {
                alert('Error selama registrasi: ' + err.message);
            }
        });
    });
</script>