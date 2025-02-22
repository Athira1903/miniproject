import { initializeApp } from "https://www.gstatic.com/firebasejs/11.2.0/firebase-app.js";
import {
  getAuth,
  GoogleAuthProvider,
  signInWithPopup,
} from "https://www.gstatic.com/firebasejs/11.2.0/firebase-auth.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.2.0/firebase-analytics.js";
const firebaseConfig = {
  apiKey: "AIzaSyAMLG8wrVIOzF8smn6B6UATy26It8RKmYs",
  authDomain: "login-dd50a.firebaseapp.com",
  projectId: "login-dd50a",
  storageBucket: "login-dd50a.firebasestorage.app",
  messagingSenderId: "332394124368",
  appId: "1:332394124368:web:a1656a805cde790f32ed55",
  measurementId: "G-VYWELB0F0N",
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
auth.languageCode = "en";
const analytics = getAnalytics(app);
const provider = new GoogleAuthProvider();

const googleLogin = document.getElementById("google-login-btn");
googleLogin.addEventListener("click", function() {
  signInWithPopup(auth, provider)
  .then((result) => {
      const credential = GoogleAuthProvider.credentialFromResult(result);
      const user = result.user;
      console.log(user);

      // Prepare user data to send to PHP
      const userData = {
          name: user.displayName,
          email: user.email,
          uid: user.uid,
          photo: user.photoURL
      };

      // Send data to PHP
      fetch('sent.php', {
          method: 'POST',
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(userData)
      })
      .then(response => response.json()) // Expect JSON response
      .then(data => {
          if (data.success) {
            console.log("adsasd");

              window.location.href = "index.php"; // Redirect if successful
          } else {
              alert("Login failed: " + data.message);
          }
      })
      .catch(error => {
          console.error("Error:", error);
          alert("Something went wrong!");
      });

  }).catch((error) => {
      console.error("Google Login Error:", error.message);
      alert("Google Login Failed!");
  });
});

