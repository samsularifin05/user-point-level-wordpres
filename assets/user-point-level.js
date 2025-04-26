document.addEventListener("DOMContentLoaded", function () {
  // Cari semua elemen dengan class upl-rank-icon
  const icons = document.querySelectorAll(".upl-rank-icon");

  // SVG string yang mau diinject
  const svg = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 214.943 214.943" width="15" height="15">
      <g>
        <path d="M187.693,46.993l-31.102-15.889L140.7,0l-33.229,10.758L74.242,0L58.354,31.104L27.25,46.993l10.758,33.228-10.757,33.23l31.102,15.889l4.247,8.314v77.288l44.875-22.431l44.868,22.432v-77.29l4.247-8.313l31.102-15.889-10.757-33.23L187.693,46.993zM107.476,175.742l-29.875,14.933v-31.317l29.871-9.67l29.872,9.671v31.316L107.476,175.742zM145.443,118.192l-12.283,24.045l-25.688-8.316l-25.686,8.316l-12.283-24.045l-24.044-12.283l8.316-25.688l-8.314-25.686l24.043-12.283l12.283-24.044l25.686,8.316l25.688-8.316l12.283,24.044l24.042,12.283-8.314,25.686l8.316,25.688L145.443,118.192z"/>
        <path d="M107.475,39.09c-22.683,0-41.137,18.451-41.137,41.13c0,22.684,18.454,41.139,41.137,41.139c22.68,0,41.132-18.455,41.132-41.139C148.607,57.542,130.155,39.09,107.475,39.09zM107.475,106.359c-14.412,0-26.137-11.726-26.137-26.139c0-14.408,11.725-26.13,26.137-26.13c14.409,0,26.132,11.722,26.132,26.13C133.607,94.634,121.884,106.359,107.475,106.359z"/>
      </g>
    </svg>
  `;

  // Loop dan inject SVG ke setiap elemen
  icons.forEach(function (icon) {
    icon.innerHTML = svg;
  });
});

document.addEventListener("DOMContentLoaded", function () {
  // Mendapatkan ID pengguna yang sedang login
  const rankImage = document.body.getAttribute("data-image-user-rank"); // Ganti dengan cara Anda mendapatkan user ID

  // console.log(rankImage, "IMAGES");
  if (rankImage) {
    // Menargetkan elemen avatar
    const avatarContainer = document.querySelector(".user-avatar");

    if (avatarContainer) {
      // Membuat elemen gambar rank
      const rankImg = document.createElement("img");
      rankImg.src = rankImage;
      rankImg.alt = "Rank Title";
      rankImg.classList.add("rank-title-img");
      rankImg.style.maxWidth = "68px"; // Sesuaikan ukuran gambar

      // Menambahkan gambar rank ke dalam kontainer avatar
      avatarContainer.insertBefore(rankImg, avatarContainer.firstChild);
    }
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const bbpress_users = bbpress_users_data?.users; // Mendapatkan data pengguna dari PHP

  if (bbpress_users) {
    // Ambil semua elemen yang mewakili 1 post/reply (yang ada user-id nya)
    const posts = document.querySelectorAll('[class*="user-id-"]');

    if (posts.length > 0) {
      posts?.forEach((post) => {
        // Ambil semua class
        const classes = post.className.split(" ");

        // Cari class yang mengandung 'user-id-'
        const userIdClass = classes.find((c) => c.startsWith("user-id-"));

        if (userIdClass) {
          const userId = userIdClass.replace("user-id-", "");

          const user = bbpress_users.find((u) => u.ID == userId);

          if (user) {
            const avatar = post.querySelector(".bbp-author-avatar");

            if (avatar && !avatar.querySelector(".rank-title-img")) {
              const rankImg = document.createElement("img");
              rankImg.src = user.rank_image;
              rankImg.alt = user.rank_level;
              rankImg.className = "rank-title-img";
              rankImg.style.position = "absolute";
              rankImg.style.maxWidth = "50px";
              rankImg.style.top = "-35%";
              rankImg.style.left = "78px";
              rankImg.style.transform = "translate(-50%, -50%)";

              avatar.style.position = "relative";
              avatar.prepend(rankImg);
            }
          }
        }
      });
    }
  }
});
