const video = document.getElementById("video");

Promise.all([
  faceapi.nets.tinyFaceDetector.loadFromUri("/models"),
  faceapi.nets.faceLandmark68Net.loadFromUri("/models"),
  faceapi.nets.faceRecognitionNet.loadFromUri("/models"),
  faceapi.nets.faceExpressionNet.loadFromUri("/models"),
]).then(startVideo);

// function startVideo() {
//   navigator.getUserMedia(
//     { video: {} },
//     (stream) => (video.srcObject = stream),
//     (err) => console.error(err)
//   );
// }
function startVideo() {
  navigator.mediaDevices.getUserMedia({ video: {} })
    .then(stream => {
      video.srcObject = stream;
    })
    .catch(err => console.error(err));
}


video.addEventListener("play", () => {
  const canvas = faceapi.createCanvasFromMedia(video);
  document.body.append(canvas);
  const displaySize = { width: video.width, height: video.height };
  faceapi.matchDimensions(canvas, displaySize);

  setInterval(async () => {
    const detections = await faceapi
      .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceExpressions();
    if (detections.length > 0) { // ตรวจสอบว่าจับใบหน้าได้หรือไม่
      const resizedDetections = faceapi.resizeResults(detections, displaySize);
      canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);
      faceapi.draw.drawDetections(canvas, resizedDetections);
      faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
      faceapi.draw.drawFaceExpressions(canvas, resizedDetections);

      // Capture the canvas as an image
      canvas.toBlob((blob) => {
        const formData = new FormData();
        formData.append("imageFile", blob, "snapshot.png"); // ส่งไฟล์ภาพไปยังเซิร์ฟเวอร์

        // ทำการ fetch ไปยังเซิร์ฟเวอร์เมื่อจับใบหน้าได้สำเร็จ
        fetch("./saveFaceData.php", {
          method: "POST",
          body: formData,
        })
          .then(response => response.json())
          .then(result => console.log("Server response:", result))
          .catch(error => console.error("Error:", error));
      }, "image/png");
    }
  }, 300); // ตั้งค่า interval ตามความต้องการ
});


