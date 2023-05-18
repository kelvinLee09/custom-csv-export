module.exports = {
  darkMode: false,
  purge: ["../**.php", "../**/**.php", "./src/js/**.js"],
  content: [],
  theme: {
    extend: {
      height: {
        "180px": "180px",
      },
      width: {
        "1px": "1px",
        "180px": "180px",
        "300px": "300px",
        "550px": "550px",
      },
    },
  },
  plugins: [require("tailwindcss"), require("precss"), require("autoprefixer")],
};
