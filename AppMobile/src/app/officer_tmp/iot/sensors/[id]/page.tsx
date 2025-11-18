export const metadata = {
  title: "Sensor Details",
};

export default function Page({ params }: { params: { id: string } }) {
  return (
    <main>
      <h1>Sensor Details</h1>
      <p>Diagnostics for a specific IoT sensor. Currently viewing record {params.id}.</p>
    </main>
  );
}
