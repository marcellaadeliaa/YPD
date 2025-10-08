<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Yayasan Purba Danarta</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        :root{
          --bg-top: #24104a;      /* ungu gelap */
          --bg-bottom: #8690a6;   /* abu kebiruan lembut */
          --card-top: rgba(47,18,76,0.95);
          --card-bottom: rgba(54,35,93,0.95);
          --pill-bg: rgba(255,255,255,0.18);
        }

        body{
          min-height: 100vh;
          font-family: 'Montserrat', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
          background: linear-gradient(180deg, var(--bg-top) 0%, rgba(48,20,86,0.7) 40%, var(--bg-bottom) 100%);
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          padding: 40px;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
        }

        /* Layout login yang lebih seimbang */
        .login-wrapper{
          width: 100%;
          max-width: 500px;
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 35px;
        }

        .logo{
          width: 320px;
          object-fit: contain;
          margin-bottom: 10px;
          filter: drop-shadow(0 5px 15px rgba(0,0,0,0.25));
        }

        .login-card{
          width: 100%;
          background: linear-gradient(180deg, var(--card-top) 0%, var(--card-bottom) 100%);
          border-radius: 28px;
          padding: 45px 40px;
          box-shadow: 0 20px 50px rgba(15,12,40,0.4);
          position: relative;
          overflow: hidden;
        }

        /* Efek dekoratif yang lebih proporsional */
        .login-card::after{
          content: "";
          position: absolute;
          left: 0; 
          bottom: 0;
          width: 180px; 
          height: 180px;
          background: linear-gradient(180deg, rgba(42,22,92,0.9), rgba(50,30,110,0.8));
          border-top-right-radius: 220px;
          transform: translate(-50%, 25%);
          opacity: 0.25;
          pointer-events: none;
        }

        .card-content{
          position: relative;
          z-index: 2;
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 28px;
          color: #fff;
          text-align: center;
        }

        /* Header yang lebih seimbang */
        .login-header {
          margin-bottom: 10px;
        }

        .login-title{
          font-family: 'Playfair Display', serif;
          font-size: 42px;
          font-weight: 400;
          letter-spacing: 0.8px;
          line-height: 1;
          margin-bottom: 12px;
        }

        .login-subtitle{
          font-family: 'Dancing Script', cursive;
          font-size: 34px;
          font-weight: 700;
          letter-spacing: 0.8px;
          color: #e9e6ff;
          margin-top: -5px;
        }

        .login-desc{
          font-size: 16px;
          line-height: 1.5;
          color: rgba(255,255,255,0.9);
          margin-bottom: 15px;
          max-width: 380px;
        }

        /* Container opsi login yang lebih rapi */
        .button-container{
          display: flex;
          flex-direction: column;
          gap: 22px;
          width: 100%;
          margin: 25px 0;
        }

        .login-option{
          display: flex;
          align-items: center;
          justify-content: flex-start;
          padding: 22px 26px;
          background: rgba(255,255,255,0.1);
          border: 2px solid rgba(255,255,255,0.2);
          border-radius: 18px;
          color: white;
          text-decoration: none;
          transition: all 0.3s ease;
          cursor: pointer;
        }

        .login-option:hover{
          background: rgba(255,255,255,0.18);
          border-color: rgba(255,255,255,0.35);
          transform: translateY(-4px);
          box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .option-icon{
          width: 48px;
          height: 48px;
          background: rgba(255,255,255,0.15);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin-right: 20px;
          font-size: 22px;
          flex-shrink: 0;
        }

        .option-text{
          flex: 1;
          text-align: left;
        }

        .option-title{
          font-size: 19px;
          font-weight: 600;
          margin-bottom: 6px;
        }

        .option-desc{
          font-size: 14px;
          color: rgba(255,255,255,0.8);
          line-height: 1.4;
        }

        .back-button{
          display: inline-flex;
          align-items: center;
          gap: 10px;
          color: rgba(255,255,255,0.8);
          text-decoration: none;
          font-size: 15px;
          margin-top: 20px;
          transition: color 0.3s ease;
          padding: 10px 16px;
          border-radius: 8px;
        }

        .back-button:hover{
          color: white;
          background: rgba(255,255,255,0.08);
        }

        /* Responsif dengan tata letak yang tetap bagus */
        @media (max-width: 600px){
          body{ 
            padding: 25px; 
          }
          
          .login-card{ 
            padding: 38px 30px; 
          }
          
          .login-title{ 
            font-size: 36px; 
          }
          
          .login-subtitle{ 
            font-size: 30px; 
          }
          
          .login-option{ 
            padding: 20px 22px; 
          }
          
          .option-icon{
            width: 44px;
            height: 44px;
            margin-right: 16px;
            font-size: 20px;
          }
          
          .option-title{
            font-size: 18px;
          }
        }

        @media (max-width: 480px){
          .login-card{ 
            padding: 32px 25px; 
          }
          
          .login-title{ 
            font-size: 32px; 
          }
          
          .login-subtitle{ 
            font-size: 26px; 
          }
          
          .login-option{
            flex-direction: column;
            text-align: center;
            gap: 15px;
          }
          
          .option-icon{
            margin-right: 0;
          }
          
          .option-text{
            text-align: center;
          }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <img src="image/namayayasan.png" alt="Logo Yayasan Purba Danarta" class="logo">

        <div class="login-card">
            <div class="card-content">
                <div class="login-header">
                    <h1 class="login-title">Login</h1>
                    <h2 class="login-subtitle">Yayasan Purba Danarta</h2>
                </div>
                
                <p class="login-desc">Silakan pilih jenis login sesuai dengan kebutuhan Anda</p>
                
                <div class="button-container">
                    <a href="login.php" class="login-option">
                        <div class="option-icon">👤</div>
                        <div class="option-text">
                            <div class="option-title">Login sebagai Pelamar</div>
                            <div class="option-desc">Untuk mendaftar dan melamar pekerjaan</div>
                        </div>
                    </a>
                    
                    <a href="login_karyawan.php" class="login-option">
                        <div class="option-icon">💼</div>
                        <div class="option-text">
                            <div class="option-title">Login sebagai Karyawan</div>
                            <div class="option-desc">Untuk karyawan tetap yayasan</div>
                        </div>
                    </a>
                </div>

                <a href="index.php" class="back-button">
                    ← Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </div>
</body>
</html>