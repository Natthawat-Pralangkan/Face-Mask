

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
let intervalId = null; // ประกาศตัวแปร intervalId ให้เป็น null เพื่อให้สามารถเข้าถึงได้ทั่วไป

video.addEventListener("play", () => {
  const canvas = faceapi.createCanvasFromMedia(video);
  document.body.append(canvas);
  const displaySize = { width: video.width, height: video.height };
  faceapi.matchDimensions(canvas, displaySize);

  let intervalId = setInterval(async () => {
    const detections = await faceapi
      .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceExpressions();

    if (detections.length > 0) {
      clearInterval(intervalId); // หยุดการเรียกฟังก์ชันที่เรียกใช้งานโดย setInterval

      const resizedDetections = faceapi.resizeResults(detections, displaySize);
      canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);
      faceapi.draw.drawDetections(canvas, resizedDetections);
      faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
      faceapi.draw.drawFaceExpressions(canvas, resizedDetections);

      // Capture the canvas as an image
      canvas.toBlob((blob) => {
        const formData = new FormData();
        formData.append("function", "insertimage_detec");
        formData.append("imageFile", blob, "snapshot.png");

        fetch("http://localhost/Face-Mask/saveFaceData.php", {
          method: "POST",
          body: formData,
        })
          .then(response => response.json())

          .then(result => {
            console.log(result.status);
            if (result.status === 200) {
              alert('124')
            } else {
              alert("e")
            }
          })
          // alert("es")
          .catch(err => alert(err))

      }, "image/png");
    }
  }, 100); // ตั้งค่า interval ตามความต้องการ
});



