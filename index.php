<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Yayasan Purba Danarta</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }


        :root{
          --bg-top: #24104a;   
          --bg-bottom: #8690a6;  
          --card-top: rgba(47,18,76,0.95);
          --card-bottom: rgba(54,35,93,0.95);
          --pill-bg: rgba(255,255,255,0.18);
        }

        body{
          min-height: 100vh;
          font-family: 'Montserrat', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
          background: linear-gradient(180deg, var(--bg-top) 0%, rgba(48,20,86,0.7) 40%, var(--bg-bottom) 100%);
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 30px;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
        }

        .main-container {
          display: flex;
          width: 1200px;
          max-width: 95%;
          gap: 60px;
          align-items: center;
          justify-content: space-between;
        }

        .visual-column {
          flex: 1;
          display: flex;
          flex-direction: column;
          gap: 35px;
          align-items: center;
        }

        .logo{
          width: 420px;
          max-width: 100%;
          object-fit: contain;
          filter: drop-shadow(0 8px 20px rgba(0,0,0,0.3));
        }

        .building-image{
          width: 100%;
          max-width: 480px;
          height: 280px;
          object-fit: cover;
          border-radius: 12px;
          box-shadow: 0 12px 30px rgba(0,0,0,0.25);
          transition: transform 0.4s ease;
        }

        .building-image:hover {
          transform: translateY(-8px);
        }

        .content-column {
          flex: 1;
          display: flex;
          justify-content: center;
          align-items: center;
        }

        .welcome-card{
          width: 100%;
          max-width: 520px;
          background: linear-gradient(180deg, var(--card-top) 0%, var(--card-bottom) 100%);
          border-radius: 28px;
          padding: 50px 45px;
          box-shadow: 0 20px 50px rgba(15,12,40,0.4);
          position: relative;
          overflow: hidden;
        }

        .welcome-card::after{
          content: "";
          position: absolute;
          left: 0; 
          bottom: 0;
          width: 220px; 
          height: 220px;
          background: linear-gradient(180deg, rgba(42,22,92,0.9), rgba(50,30,110,0.8));
          border-top-right-radius: 250px;
          transform: translate(-40%, 30%);
          opacity: 0.3;
          pointer-events: none;
        }

        .card-content{
          position: relative;
          z-index: 2;
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 25px;
          color: #fff;
          text-align: center;
        }

        .welcome-section {
          margin-bottom: 15px;
        }

        .welcome-text{
          font-size: 18px;
          color: rgba(255,255,255,0.9);
          font-weight: 300;
          letter-spacing: 2px;
          margin-bottom: 15px;
          text-transform: uppercase;
        }

        .main-title{
          font-family: 'Playfair Display', serif;
          font-size: 58px;
          margin: 15px 0;
          font-weight: 400;
          letter-spacing: 1px;
          line-height: 1.1;
        }

        .subtitle{
          font-family: 'Dancing Script', cursive;
          font-size: 46px;
          margin-top: -5px;
          font-weight: 700;
          letter-spacing: 1px;
          color: #e9e6ff;
          text-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .description-container{
          width: 90%;
          background: var(--pill-bg);
          padding: 28px 32px;
          border-radius: 18px;
          margin: 20px 0;
          box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
          border: 1px solid rgba(255,255,255,0.1);
        }

        .description-container p{
          font-size: 18px;
          line-height: 1.6;
          color: #fff;
          font-weight: 600;
          margin: 0;
          text-align: center;
        }

        .cta-button{
          width: 200px;
          height: 70px;
          border-radius: 45px;
          background: #ffffff;
          border: none;
          font-family: 'Playfair Display', serif;
          font-size: 26px;
          font-weight: 700;
          color: #111;
          cursor: pointer;
          box-shadow: 0 12px 25px rgba(0,0,0,0.3);
          transition: all 0.3s ease;
          text-decoration: none;
          display: flex;
          align-items: center;
          justify-content: center;
          margin-top: 15px;
        }

        .cta-button:hover{
          transform: translateY(-6px);
          box-shadow: 0 18px 35px rgba(0,0,0,0.4);
        }

        @media (max-width: 1100px){
          .main-container {
            flex-direction: column;
            gap: 50px;
          }
          
          .visual-column, .content-column {
            width: 100%;
          }
          
          .logo{
            width: 360px;
          }
          
          .building-image{
            max-width: 420px;
            height: 250px;
          }
          
          .welcome-card{
            max-width: 100%;
            padding: 45px 35px;
          }
        }

        @media (max-width: 768px){
          body{
            padding: 20px;
          }
          
          .welcome-card{
            padding: 40px 30px;
          }
          
          .main-title{
            font-size: 48px;
          }
          
          .subtitle{
            font-size: 38px;
          }
          
          .description-container{
            padding: 24px 28px;
          }
          
          .description-container p{
            font-size: 17px;
          }
          
          .cta-button{
            width: 180px;
            height: 65px;
            font-size: 24px;
          }
        }

        @media (max-width: 480px){
          .main-title{
            font-size: 40px;
          }
          
          .subtitle{
            font-size: 32px;
          }
          
          .welcome-card{
            padding: 35px 25px;
          }
          
          .description-container{
            width: 95%;
            padding: 20px;
          }
          
          .welcome-text{
            font-size: 16px;
          }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="visual-column">
            <img src="image/namayayasan.png" alt="Logo Yayasan Purba Danarta" class="logo">
            <img src="image/gedungyayasan.png" alt="Gedung Yayasan Purba Danarta" class="building-image">
        </div>
        
        <div class="content-column">
            <div class="welcome-card">
                <div class="card-content">
                    <div class="welcome-section">
                        <p class="welcome-text">Selamat Datang di</p>
                        <h1 class="main-title">Yayasan</h1>
                        <h2 class="subtitle">Purba Danarta</h2>
                    </div>
                    
                    <div class="description-container">
                        <p>Mari Usahakan pembangunan dan pengembangan masyarakat sektor ekonomi kecil</p>
                    </div>
                    
                    <a href="dashboardlogin.php" class="cta-button">Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>