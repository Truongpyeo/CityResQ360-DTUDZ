export const metadata = {
  title: "Sensor Details",
};

type PageParams = Promise<{ id: string }>;

export default async function Page({ params }: { params: PageParams }) {
  const { id } = await params;

  return (
    <main>
      <h1>Sensor Details</h1>
      <p>Diagnostics for a specific IoT sensor. Currently viewing record {id}.</p>
    </main>
  );
}
