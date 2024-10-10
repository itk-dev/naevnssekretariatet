import './app.scss'
import * as bootstrap from 'bootstrap'
import '@popperjs/core'
import 'bs-custom-file-input'
import 'select2'

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')

window.addEventListener('load', () => {
  [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
  window.dispatchEvent(new Event('ajaxload'))

  const recipientSelector = '.digital-post-recipient'
  const digitalPostRecipientWrapper = document.querySelector(recipientSelector)?.parentNode?.parentNode
  const digitalPostAttachmentsInfo = document.querySelector('.digital-post-attachments-info')
  if (digitalPostRecipientWrapper && digitalPostAttachmentsInfo) {
    const recipients = digitalPostRecipientWrapper.querySelectorAll(recipientSelector)
    const updateDigitalPostInfo = () => {
      const recipientsThatCannotReceiveDigitalPost = [...recipients]
        .filter(el => !el.dataset.digitalPostAllowed &&
                el.querySelector('input[type="checkbox"]').checked);

      // Show only the appropriate digital post message (cf.
      // ../templates/common/digital_post_attachments_info.html.twig).
      [...digitalPostAttachmentsInfo.querySelectorAll('.digital-post-attachments-info-item')]
        .forEach(el => {
          el.hidden = !(el.dataset.digitalPostAllowed ^ (recipientsThatCannotReceiveDigitalPost.length > 0))
        })
    }

    [...recipients].forEach(el => el.addEventListener('change', (event) => updateDigitalPostInfo()))
    updateDigitalPostInfo()
  }
})
