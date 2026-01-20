document.addEventListener('DOMContentLoaded', function () {
    // Close button functionality for the notice
    const closeBtn = document.querySelector('.mpesapaywallpro-notice-close');
    const notice = document.querySelector('.mpesapaywallpro-notice');
    //check if cookie is set to hide notice
    const closedNotice = get_cookie("mpesapaywallpro_notice_closed");
    if (closedNotice === "true" && notice) {
        notice.style.display = 'none';
    }
    
    //add event listener to close button
    if (closeBtn && notice) {
        closeBtn.addEventListener('click', function () {
            notice.style.display = 'none';
            document.cookie = "mpesapaywallpro_notice_closed=true; path=/; max-age=" + 60 * 60 * 24 * 30; // 30 days
        });
    }
});

// function to get cookie
function get_cookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}