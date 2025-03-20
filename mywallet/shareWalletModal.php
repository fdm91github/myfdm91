<!-- Modal per la condivisione del portafoglio -->
<div class="modal fade" id="shareWalletModal" tabindex="-1" aria-labelledby="shareWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shareWalletModalLabel">Condividi Portafoglio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="shareWalletStatus"></div>
        <form id="shareWalletForm">
          <input type="hidden" name="wallet_id" id="shareWalletId">
          <div class="form-group mb-3">
            <label for="shareUsername">Nome utente con cui condividere</label>
            <input type="text" name="share_username" id="shareUsername" class="form-control" placeholder="Username" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Condividi</button>
        </form>
        <hr>
        <h6>Utenti con cui è condiviso:</h6>
        <div id="currentShares">
          <!-- La lista degli utenti condivisi verrà popolata qui -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Function to update the current shared users list
  function updateSharedList(sharedArray, walletId) {
    var container = $('#currentShares');
    container.empty();
    if(sharedArray.length === 0) {
      container.html("<p>Nessun utente condiviso.</p>");
      return;
    }
    var list = $('<ul class="list-group"></ul>');
    sharedArray.forEach(function(entry) {
      var listItem = $('<li class="list-group-item d-flex justify-content-between align-items-center"></li>');
      listItem.text(entry.username);
      var removeBtn = $('<button class="btn btn-sm btn-danger remove-share"><i class="bi bi-x-circle"></i></button>');
      removeBtn.data('user-id', entry.id);
      removeBtn.data('wallet-id', walletId);
      listItem.append(removeBtn);
      list.append(listItem);
    });
    container.append(list);
  }
  
  $(document).ready(function(){
    // When the share modal is shown, set the wallet ID and populate current shared users.
    // Use .attr('data-shared') to get the latest raw attribute value.
    $('#shareWalletModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var walletId = button.data('id');
      var sharedDataRaw = button.attr('data-shared'); // Get the raw attribute value
      var sharedData;
      if(sharedDataRaw && sharedDataRaw.trim() !== "") {
        try {
          sharedData = JSON.parse(sharedDataRaw);
        } catch(e) {
          sharedData = [];
        }
      } else {
        sharedData = [];
      }
      $('#shareWalletId').val(walletId);
      $('#shareWalletForm')[0].reset();
      $('#shareWalletStatus').html('');
      updateSharedList(sharedData, walletId);
    });
    
    // Form submission for adding a share
    $('#shareWalletForm').submit(function(e){
      e.preventDefault();
      $('button[type="submit"]').prop('disabled', true);
      $.ajax({
        url: 'shareWallet.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
          if(response.status === 'success') {
            $('#shareWalletStatus').html('<div class="alert alert-success">' + response.message + '</div>');
            $('#shareWalletForm')[0].reset();
            // Refresh the page after a short delay to update all data
            setTimeout(function(){ $('#shareWalletModal').modal('hide'); window.location.reload(); }, 1000);
          } else {
            $('#shareWalletStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
          }
          $('button[type="submit"]').prop('disabled', false);
        },
        error: function() {
          $('#shareWalletStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
          $('button[type="submit"]').prop('disabled', false);
        }
      });
    });
    
    // Event delegation for removing a shared user
    $('#currentShares').on('click', '.remove-share', function(){
      var btn = $(this);
      var removeUserId = btn.data('user-id');
      var walletId = btn.data('wallet-id');
      
      if(confirm("Sei sicuro di voler rimuovere questo utente dalla condivisione?")) {
        $.ajax({
          url: 'removeSharedUser.php',
          type: 'POST',
          data: { wallet_id: walletId, remove_user_id: removeUserId },
          success: function(response) {
            if(response.status === 'success') {
              btn.closest('li').remove();
              $('#shareWalletStatus').html('<div class="alert alert-success">' + response.message + '</div>');
              if($('#currentShares ul li').length === 0) {
                $('#currentShares').html("<p>Nessun utente condiviso.</p>");
              }
            } else {
              $('#shareWalletStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
          },
          error: function() {
            $('#shareWalletStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
          }
        });
      }
    });
  });
</script>
