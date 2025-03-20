<!-- Modal per rimuovere la condivisione del portafoglio -->
<div class="modal fade" id="discardShareModal" tabindex="-1" aria-labelledby="discardShareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="discardShareModalLabel">Rimuovi Portafogli</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="discardShareStatus"></div>
        <p>Sei sicuro di voler rimuovere la condivisione per questo portafoglio?</p>
		<p>Una volta rimossa, non potrai più accedere a tutto il suo contenuto, a meno che il proprietario non ti aggiunga nuovamente.</p>
		<p><i>Nota: questa azione non eliminerà il portafogli, né le spese da te aggiunte al suo interno, che saranno ancora visibili al proprietario ed agli altri partecipanti.</i></p>
        <form id="discardShareForm">
          <input type="hidden" name="wallet_id" id="discardShareWalletId">
          <button type="submit" class="btn btn-danger w-100">Conferma rimozione</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
    // When the discard share modal is shown, set the wallet id from the triggering button.
    $('#discardShareModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var walletId = button.data('wallet-id');
        $('#discardShareWalletId').val(walletId);
        $('#discardShareStatus').html('');
    });

    // Form submission for discarding share.
    $('#discardShareForm').submit(function(e){
        e.preventDefault();
        $('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: 'discardShare.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    $('#discardShareStatus').html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(function(){
                        $('#discardShareModal').modal('hide'); 
                        window.location.reload();
                    }, 1000);
                } else {
                    $('#discardShareStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
                $('button[type="submit"]').prop('disabled', false);
            },
            error: function() {
                $('#discardShareStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                $('button[type="submit"]').prop('disabled', false);
            }
        });
    });
});
</script>
